<?php

namespace AppBundle\Repository;

/**
 * NotificationClientRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class NotificationClientRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param $id
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getNotify($id){
        return $this->createQueryBuilder('nc')
            ->select('nc.notifyEmail,nc.newCandidateStatus,nc.newCandidate, nc.jobApproveStatus, nc.jobApprove, nc.jobDeclineStatus, nc.jobDecline, nc.candidateApplicantStatus, nc.candidateApplicant, nc.candidateDeclineStatus, nc.candidateDecline')
            ->where("nc.user = :id")
            ->setParameter('id',$id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}